<?php

namespace Blog\PostBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Blog\CommentBundle\Entity\Comment;
use Blog\PostBundle\Entity\Post;
use Blog\PostBundle\Form\PostType;
use Blog\PostBundle\Entity\PostComment;
use Blog\PostBundle\Form\PostCommentType;
use Blog\PostBundle\Controller\PostCommentController;

/**
 * Post controller.
 *
 * @Route("/post/")
 */
class PostController extends Controller
{

    /**
     * Lists all Post entities.
     *
     * @Route("/", name="post")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BlogPostBundle:Post')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/", name="post_create")
     * @Method("POST")
     * @Template("BlogPostBundle:Post:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Post();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $entity->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array('slug' => $entity->getSlug())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Post entity.
     *
     * @param Post $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('post_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Post entity.
     *
     * @Route("/new", name="post_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Post();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{slug}", name="post_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BlogPostBundle:Post')->findBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $commentForm = $this->newPostCommentAction($slug);

        $deleteForm = $this->createDeleteForm($slug);

        return array(
            'entity'      => $entity[0],
            'delete_form' => $deleteForm->createView(),
            'comment_form' => $commentForm['form'],
        );
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{slug}/edit", name="post_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BlogPostBundle:Post')->findBySlug($slug);

        if (!$entity[0]) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $editForm = $this->createEditForm($entity[0]);
        $deleteForm = $this->createDeleteForm($slug);

        return array(
            'entity'      => $entity[0],
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Post entity.
    *
    * @param Post $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Post $entity)
    {
        $form = $this->createForm(new PostType(), $entity, array(
            'action' => $this->generateUrl('post_update', array('slug' => $entity->getSlug())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Post entity.
     *
     * @Route("/{slug}", name="post_update")
     * @Method("PUT")
     * @Template("BlogPostBundle:Post:edit.html.twig")
     */
    public function updateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BlogPostBundle:Post')->findBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }

        $deleteForm = $this->createDeleteForm($slug);
        $editForm = $this->createEditForm($entity[0]);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('post_edit', array('slug' => $slug)));
        }

        return array(
            'entity'      => $entity[0],
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Post entity.
     *
     * @Route("/{slug}", name="post_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $slug)
    {
        $form = $this->createDeleteForm($slug);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BlogPostBundle:Post')->findBySlug($slug);

            if (!$entity[0]) {
                throw $this->createNotFoundException('Unable to find Post entity.');
            }

            $em->remove($entity[0]);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('post'));
    }

    /**
     * Creates a form to delete a Post entity by slug.
     *
     * @param mixed $slug The entity slug
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($slug)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('slug' => $slug)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    // Adding comment entity to post

    /**
     * Creates a new Comment entity.
     *
     * @Route("/post/comment/create", name="post_comment_create")
     * @Method("POST")
     * @Template("BlogCommentBundle:Comment:new.html.twig")
     */
    public function createPostCommentAction(Request $request)
    {
        $entity = new Comment();

        $form = $this->createPostCommentCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $entity->setUser($this->getUser());

            $allRequest = $request->request->all();
            $token = $allRequest["blog_post_bundle_post_comment"]['token'];

            $em = $this->getDoctrine()->getManager();

            $entity->setComment($allRequest["blog_post_bundle_post_comment"]['comment']['comment']);

            $post = $em->getRepository('BlogPostBundle:Post')->findBySlug($token);
            $post[0]->setComment($entity);

            $em->persist($entity);
            $em->persist($post[0]);
            $em->flush();

            return $this->redirect($this->generateUrl('post_show', array('slug' => $token)));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Comment entity.
     *
     * @param Comment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createPostCommentCreateForm(Comment $entity, $slug = null)
    {
        $form = $this->createForm(new PostCommentType(), $entity, array(
            'action' => $this->generateUrl('post_comment_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));
        $form->add('token', 'hidden', array('data' => $slug, 'mapped' => false));

        return $form;
    }

    /**
     * Displays a form to create a new Comment entity.
     *
     * @Route("/post/comment/new", name="post_comment_new")
     * @Method("GET")
     * @Template()
     */
    public function newPostCommentAction($slug)
    {
        $entity = new Comment();
        $form   = $this->createPostCommentCreateForm($entity, $slug);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
}
