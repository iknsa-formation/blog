<?php

namespace Blog\PostBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Blog\CommentBundle\Entity\Comment;
use Blog\PostBundle\Entity\Post;
use Blog\PostBundle\Form\PostCommentType;
use Blog\PostBundle\Controller\PostCommentController;

/**
 * Post controller.
 *
 * @Route("/post/")
 */
class PostCommentController extends Controller
{
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('post_comment_show', array('slug' => $entity->getSlug())));
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
    private function createPostCommentCreateForm(Comment $entity)
    {
        $form = $this->createForm(new PostCommentType(), $entity, array(
            'action' => $this->generateUrl('post_comment_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

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
        $form   = $this->createPostCommentCreateForm($entity);

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository('BlogPostBundle:Post')->findBySlug($slug);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
}
